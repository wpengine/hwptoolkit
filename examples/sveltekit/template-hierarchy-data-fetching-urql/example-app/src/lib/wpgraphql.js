export function flatListToHierarchical(
  data = [],
  { idKey = "id", parentKey = "parentId", childrenKey = "children" } = {}
) {
  const tree = [];
  const childrenOf = {};

  for (const item of data) {
    const newItem = { ...item };

    const id = newItem?.[idKey];
    const parentId = newItem?.[parentKey] ?? 0;

    if (!id) {
      continue;
    }

    childrenOf[id] = childrenOf[id] || [];
    newItem[childrenKey] = childrenOf[id];

    parentId
      ? (childrenOf[parentId] = childrenOf[parentId] || []).push(newItem)
      : tree.push(newItem);
  }
  return tree;
}
